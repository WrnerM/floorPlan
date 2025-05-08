USE [FloorPlan]
GO

/****** Object:  StoredProcedure [dbo].[usp_site_summary]    Script Date: 5/8/2025 12:46:12 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO


--exec [usp_site_summary] @dateValid = '2022-09-07 00:00:00.0000000'

CREATE OR ALTER                         procedure [dbo].[usp_site_summary](

	@dateValid as nvarchar(50)

)

as begin 

	set nocount on
 
	declare @tbl as table (

		tags varchar(50)

	)
 
	declare @query nvarchar(max); 

	declare @pvt_columns nvarchar(max);

	declare @cols_def nvarchar(max);
 
	insert into @tbl

	select distinct tag from positions 

	where tag is not null and ltrim(rtrim(tag)) <> ''
 
	select @pvt_columns = STRING_AGG(QUOTENAME(tags), ', '),

			@cols_def = STRING_AGG(QUOTENAME(tags), ' + ')

	from (select tags FROM @tbl) as z;
 
 
	set @query = N'

				select siteid, '+@pvt_columns+','+@cols_def+' as total_seats

				from (

					select floorid, siteid, tag from positions a

						inner join floors b on a.floorid = b.id

					where dateValid = '''+@dateValid+'''

				) as src

				pivot (

					COUNT(floorid) for tag in ('+@pvt_columns+')

				) as pvt_table;

		';
 
	execute(@query);
 
	end

 

GO
